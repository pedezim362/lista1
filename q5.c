#include <stdio.h>

int main() {

    float alt, pi, pa;
    char gen;

    puts("Qual é a sua altura? ");
    scanf("%f", &alt);

    puts("Qual é o seu gênero? (M/F) ");
    scanf(" %c", &gen);

    if (gen == 'M' || gen == 'm') {
        pi = (72.7 * alt) - 58;
        printf("O seu peso ideal é: %.2f kg.\n", pi);
    } else if (gen == 'F' || gen == 'f') {
        pi = (62.1 * alt) - 44.7;
        printf("O seu peso ideal é: %.2f kg.\n", pi);
    } else {
        puts("Gênero inválido.");
    }

    puts("Qual é o seu peso atual? ");
    scanf("%f", &pa);

    if (pa > pi) {
        printf("Você está acima do peso ideal em %.2f kg.\n", pa - pi);
    } else if (pa < pi) {
        printf("Você está abaixo do peso ideal em %.2f kg.\n", pi - pa);
    } else {
        puts("Você está no peso ideal.");
    }

    return 0;
}