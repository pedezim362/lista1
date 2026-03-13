#include <stdio.h>

int main() {

    double h, m, s;
    puts("Digite um valor em horas: ");
    scanf("%lf", &h);
    puts("Digite um valor em minutos: ");
    scanf("%lf", &m);  
    puts("Digite um valor em segundos: ");
    scanf("%lf", &s);
    printf("O tempo total é %.2f s.\n", (h * 3600) + (m * 60) + s);

    return 0;
}