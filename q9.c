#include <stdio.h>

int main ( ) {

    int c, l, a;
    puts("Informe o comprimento do objeto (em metros): ");
    scanf("%d", &c);
    puts("Informe a largura do objeto (em metros): ");
    scanf("%d", &l);
    puts("Informe a altura do objeto (em metros): ");
    scanf("%d", &a);

    printf("O volume do objeto e: %d m³.\n", c*l*a);

    return 0;
}